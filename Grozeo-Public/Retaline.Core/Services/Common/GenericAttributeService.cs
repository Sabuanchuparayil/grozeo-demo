using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Retaline.Core;
using Retaline.Core.BusinessModel.Common;
using Retaline.Core.Caching;
using Retaline.Core.Data;
using Retaline.Core.Infra;
using Retaline.Core.Services.HelperServices;



namespace Retaline.Core.Services.Common
{
    /// <summary>
    /// Generic attribute service
    /// </summary>
    public partial class GenericAttributeService : IGenericAttributeService
    {
        #region Fields

        //private readonly IRepository<GenericAttribute> _genericAttributeRepository;
        private readonly IStaticCacheManager _staticCacheManager;
        private readonly IDBService _dbContext;

        #endregion

        #region Ctor

        public GenericAttributeService(IDBService dbContext,
            IStaticCacheManager staticCacheManager)
        {
            //_genericAttributeRepository = genericAttributeRepository;
            _staticCacheManager = staticCacheManager;
            _dbContext = dbContext;
        }

        #endregion

        #region Methods

        /// <summary>
        /// Deletes an attribute
        /// </summary>
        /// <param name="attribute">Attribute</param>
        /// <returns>A task that represents the asynchronous operation</returns>
        public virtual async Task DeleteAttributeAsync(GenericAttribute attribute)
        {
            throw new NotImplementedException();
            //await _genericAttributeRepository.DeleteAsync(attribute);
        }

        /// <summary>
        /// Deletes an attributes
        /// </summary>
        /// <param name="attributes">Attributes</param>
        /// <returns>A task that represents the asynchronous operation</returns>
        public virtual async Task DeleteAttributesAsync(IList<GenericAttribute> attributes)
        {
            throw new NotImplementedException();
            //await _genericAttributeRepository.DeleteAsync(attributes);
        }

        /// <summary>
        /// Inserts an attribute
        /// </summary>
        /// <param name="attribute">attribute</param>
        /// <returns>A task that represents the asynchronous operation</returns>
        public virtual async Task InsertAttributeAsync(GenericAttribute attribute)
        {
            if (attribute == null)
                throw new ArgumentNullException(nameof(attribute));

            attribute.CreatedOrUpdatedDateUTC = DateTime.UtcNow;
            throw new NotImplementedException();

            //await _genericAttributeRepository.InsertAsync(attribute);
        }

        /// <summary>
        /// Updates the attribute
        /// </summary>
        /// <param name="attribute">Attribute</param>
        /// <returns>A task that represents the asynchronous operation</returns>
        public virtual async Task UpdateAttributeAsync(GenericAttribute attribute)
        {
            if (attribute == null)
                throw new ArgumentNullException(nameof(attribute));

            attribute.CreatedOrUpdatedDateUTC = DateTime.UtcNow;
            throw new NotImplementedException();

            //await _genericAttributeRepository.UpdateAsync(attribute);
        }

        /// <summary>
        /// Get attributes
        /// </summary>
        /// <param name="entityId">Entity identifier</param>
        /// <param name="keyGroup">Key group</param>
        /// <returns>
        /// A task that represents the asynchronous operation
        /// The task result contains the get attributes
        /// </returns>
        public virtual async Task<IList<GenericAttribute>> GetAttributesForEntityAsync(int entityId, string keyGroup)
        {
            
            var key = _staticCacheManager.PrepareKeyForShortTermCache(RetalineCommonDefaults.GenericAttributeCacheKey, entityId, keyGroup);            
            var attributes = await _staticCacheManager.GetAsync(key, async () => await _dbContext.GetGenericAttributeFromDB(entityId, keyGroup));

            return attributes;
        }

        /// <summary>
        /// Save attribute value
        /// </summary>
        /// <typeparam name="TPropType">Property type</typeparam>
        /// <param name="entity">Entity</param>
        /// <param name="key">Key</param>
        /// <param name="value">Value</param>
        /// <param name="storeId">Store identifier; pass 0 if this attribute will be available for all stores</param>
        /// <returns>A task that represents the asynchronous operation</returns>
        public virtual async Task SaveAttributeAsync<TPropType>(BaseEntity entity, string key, TPropType value, int storeId = 0)
        {
            if (entity == null)
                throw new ArgumentNullException(nameof(entity));

            if (key == null)
                throw new ArgumentNullException(nameof(key));

            var keyGroup = entity.GetType().Name;

            var props = (await GetAttributesForEntityAsync(entity.Id, keyGroup))
                .Where(x => x.StoreId == storeId)
                .ToList();
            var prop = props.FirstOrDefault(ga =>
                ga.Key.Equals(key, StringComparison.InvariantCultureIgnoreCase)); //should be culture invariant

            var valueStr = CommonHelper.To<string>(value);

            if (prop != null)
            {
                if (!string.IsNullOrWhiteSpace(valueStr))
                    prop.Value = valueStr;
                await _dbContext.SaveGenericAttributeInDB(prop);
            }
            else
            {
                if (string.IsNullOrWhiteSpace(valueStr)) 
                    return;

                //insert
                prop = new GenericAttribute
                {
                    EntityId = entity.Id,
                    Key = key,
                    KeyGroup = keyGroup,
                    Value = valueStr,
                    StoreId = storeId
                };

                await _dbContext.SaveGenericAttributeInDB(prop);
            }
        }

        /// <summary>
        /// Save attribute value
        /// </summary>
        /// <typeparam name="TPropType">Property type</typeparam>
        /// <param name="entityId">Entity Id</param>
        /// <param name="keyGroup">key Group</param>
        /// <param name="key">Key</param>
        /// <param name="value">Value</param>
        /// <param name="storeId">Store identifier; pass 0 if this attribute will be available for all stores</param>
        /// <returns>A task that represents the asynchronous operation</returns>
        public virtual async Task SaveAttributeAsync<TPropType>(int entityId, string keyGroup, string key, TPropType value, int storeId = 0)
        {
            if (entityId <0)
                throw new ArgumentNullException(nameof(entityId));

            if (key == null)
                throw new ArgumentNullException(nameof(key));

            //var keyGroup = entity.GetType().Name;

            var props = (await GetAttributesForEntityAsync(entityId, keyGroup))
                .Where(x => x.StoreId == storeId)
                .ToList();
            var prop = props.FirstOrDefault(ga =>
                ga.Key.Equals(key, StringComparison.InvariantCultureIgnoreCase)); //should be culture invariant

            var valueStr = CommonHelper.To<string>(value);

            if (prop != null)
            {
                if (!string.IsNullOrWhiteSpace(valueStr))
                    prop.Value = valueStr;
                await _dbContext.SaveGenericAttributeInDB(prop);
            }
            else
            {
                if (string.IsNullOrWhiteSpace(valueStr))
                    return;

                //insert
                prop = new GenericAttribute
                {
                    EntityId = entityId,
                    Key = key,
                    KeyGroup = keyGroup,
                    Value = valueStr,
                    StoreId = storeId
                };

                await _dbContext.SaveGenericAttributeInDB(prop);
            }
        }


        /// <summary>
        /// Get an attribute of an entity
        /// </summary>
        /// <typeparam name="TPropType">Property type</typeparam>
        /// <param name="entity">Entity</param>
        /// <param name="key">Key</param>
        /// <param name="storeId">Load a value specific for a certain store; pass 0 to load a value shared for all stores</param>
        /// <param name="defaultValue">Default value</param>
        /// <returns>
        /// A task that represents the asynchronous operation
        /// The task result contains the attribute
        /// </returns>
        public virtual async Task<TPropType> GetAttributeAsync<TPropType>(BaseEntity entity, string key, int storeId = 0, TPropType defaultValue = default)
        {
            if (entity == null)
                throw new ArgumentNullException(nameof(entity));

            var keyGroup = entity.GetType().Name;

            var props = await GetAttributesForEntityAsync(entity.Id, keyGroup);

            //little hack here (only for unit testing). we should write expect-return rules in unit tests for such cases
            if (props == null)
                return defaultValue;

            props = props.Where(x => x.StoreId == storeId).ToList();
            if (!props.Any())
                return defaultValue;

            var prop = props.FirstOrDefault(ga =>
                ga.Key.Equals(key, StringComparison.InvariantCultureIgnoreCase)); //should be culture invariant

            if (prop == null || string.IsNullOrEmpty(prop.Value))
                return defaultValue;

            return CommonHelper.To<TPropType>(prop.Value);
        }

        /// <summary>
        /// Get an attribute of an entity
        /// </summary>
        /// <typeparam name="TPropType">Property type</typeparam>
        /// <param name="entity">Entity Id</param>
        /// <param name="keyGroup">keyGroup</param>
        /// <param name="key">Key</param>
        /// <param name="storeId">Load a value specific for a certain store; pass 0 to load a value shared for all stores</param>
        /// <param name="defaultValue">Default value</param>
        /// <returns>
        /// A task that represents the asynchronous operation
        /// The task result contains the attribute
        /// </returns>
        public virtual async Task<TPropType> GetAttributeAsync<TPropType>(int entityId, string keyGroup, string key, int storeId = 0, TPropType defaultValue = default)
        {
            //if (entity == null)
            //    throw new ArgumentNullException(nameof(entity));

            //var keyGroup = entity.GetType().Name;

            var props = await GetAttributesForEntityAsync(entityId, keyGroup);

            //little hack here (only for unit testing). we should write expect-return rules in unit tests for such cases
            if (props == null)
                return defaultValue;

            props = props.Where(x => x.StoreId == storeId).ToList();
            if (!props.Any())
                return defaultValue;

            var prop = props.FirstOrDefault(ga =>
                ga.Key.Equals(key, StringComparison.InvariantCultureIgnoreCase)); //should be culture invariant

            if (prop == null || string.IsNullOrEmpty(prop.Value))
                return defaultValue;

            return CommonHelper.To<TPropType>(prop.Value);
        }


        /// <summary>
        /// Get an attribute of an entity
        /// </summary>
        /// <typeparam name="TPropType">Property type</typeparam>
        /// <typeparam name="TEntity">Entity type</typeparam>
        /// <param name="entityId">Entity identifier</param>
        /// <param name="key">Key</param>
        /// <param name="storeId">Load a value specific for a certain store; pass 0 to load a value shared for all stores</param>
        /// <param name="defaultValue">Default value</param>
        /// <returns>
        /// A task that represents the asynchronous operation
        /// The task result contains the attribute
        /// </returns>
        public virtual async Task<TPropType> GetAttributeAsync<TEntity, TPropType>(int entityId, string key, int storeId = 0, TPropType defaultValue = default)
            where TEntity : BaseEntity
        {
            var entity = (TEntity)Activator.CreateInstance(typeof(TEntity));
            entity.Id = entityId;

            return await GetAttributeAsync(entity, key, storeId, defaultValue);
        }

        #endregion
    }
}